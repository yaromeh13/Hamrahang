package ir.hamrahang.tv.util

object ImageResolver {


    fun thumb(value:String?):String {

        if(value.isNullOrEmpty())
            return ""

        return "https://hamrahang.ir/thumb/$value.jpg"
    }


    fun art(value:String?):String {

        if(value.isNullOrEmpty())
            return ""

        return "https://hamrahang.ir/art/$value.jpg"
    }

}